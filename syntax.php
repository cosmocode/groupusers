<?php
/**
 * Syntax Plugin:
 * This plugin lists all users from the given groups in a tabel.
 * Syntax:
 * {{groupusers:<group1>[,group2[,group3...]]}}
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Dominik Eckelmann <eckelmann@cosmocode.de>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_groupusers extends DokuWiki_Syntax_Plugin {

    function groupusers() { }
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Dominik Eckelmann',
            'email'  => 'dokuwiki@cosmocode.de',
            'date'   => '2009-07-09',
            'name'   => 'Groupusers Syntax plugin',
            'desc'   => 'Displays the users from one or more groups.',
            'url'    => 'http://www.dokuwiki.org/plugin:groupusers'
        );
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'normal';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 160;
    }

    function connectTo($mode) {
         $this->Lexer->addSpecialPattern('\{\{groupusers\>[^}]*?\}\}',$mode,'plugin_groupusers');
         $this->Lexer->addSpecialPattern('\{\{groupusers\|nomail\>[^}]*?\}\}',$mode,'plugin_groupusers');
    }

    function handle($match, $state, $pos, Doku_Handler $handler){
        $match = substr($match,13,-2);
        $data = array(null, $state, $pos);
		if (substr($match, 0, 7) == 'nomail>') 
        {
            $match = substr($match, 7);
            $data[] = 'nomail';
		}

        $match = explode(',',$match);
        
        $data[0] = $match;
		return $data;
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        global $auth;
        global $lang;

        if (!method_exists($auth,"retrieveUsers")) return false;
        if($mode == 'xhtml'){
            $users = array();
            foreach ($data[0] as $grp) {
                $getuser = $auth->retrieveUsers(0,-1,array('grps'=>'^'.preg_quote($grp,'/').'$'));
                $users = array_merge($users,$getuser);
            }
            $renderer->doc .= $match.'<table class="inline">';
            $renderer->doc .= '<tr>';
            $renderer->doc .= '<th>'.$lang['user'].'</th>';
            $renderer->doc .= '<th>'.$lang['fullname'].'</th>';
            
            if (!in_array('nomail', $data))
			{
				$renderer->doc .= '<th>'.$lang['email'].'</th>';
			}

            $renderer->doc .= '</tr>';
            foreach ($users as $user => $info) {
                $renderer->doc .= '<tr>';
                $renderer->doc .= '<td>'.htmlspecialchars($user).'</td>';
                $renderer->doc .= '<td>'.hsc($info['name']).'</td>';

                if (!in_array('nomail', $data))
				{
                    $renderer->doc .= '<td>';
					$renderer->emaillink($info['mail']);
                    $renderer->doc .= '</td>';
				}

                $renderer->doc .= '</tr>';
            }
            $renderer->doc .= '</table>';
            return true;
        }
        return false;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
